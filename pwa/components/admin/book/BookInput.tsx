import { SyntheticEvent, useMemo, useRef, useState } from "react";
import Autocomplete from "@mui/material/Autocomplete";
import { debounce } from "@mui/material";
import { TextInput, type TextInputProps, useInput } from "react-admin";
import { useQuery } from "react-query";
import { useWatch } from "react-hook-form";

import { Search } from "@/types/OpenLibrary/Search";
import { SearchDoc } from "@/types/OpenLibrary/SearchDoc";

interface Result {
  title: string;
  author: string;
  value: string;
}

interface BookInputProps extends TextInputProps {
  title?: string;
  author?: string;
}

const fetchOpenLibrarySearch = async (query: string, signal?: AbortSignal | undefined): Promise<Array<Result>> => {
  try {
    const response = await fetch(`https://openlibrary.org/search.json?q=${query.replace(/ - /, ' ')}&limit=10`, {
      signal,
      method: "GET"
    });
    const results: Search = await response.json();

    return results.docs
        .filter((result: SearchDoc) => {
          return typeof result.title !== "undefined"
              && typeof result.author_name !== "undefined"
              && result.author_name.length > 0
              && typeof result.seed !== "undefined"
              && result.seed.length > 0
              && result.seed.filter((seed) => seed.match(/^\/books\/OL\d{7}M/)).length > 0;
        })
        .map(({ title, author_name, seed }): Result => {
          return {
            // @ts-ignore
            title,
            // @ts-ignore
            author: author_name[0],
            // @ts-ignore
            value: `https://openlibrary.org${seed.filter((seed) => seed.match(/^\/books\/OL\d{7}M/))[0]}.json`,
          };
        });
  } catch (error) {
    console.error(error);

    return Promise.resolve([]);
  }
};

export const BookInput = (props: BookInputProps) => {
  const { field: { ref, ...field} } = useInput(props);
  const title = useWatch({ name: "title" });
  const author = useWatch({ name: "author" });
  const controller = useRef<AbortController | undefined>();
  const [searchQuery, setSearchQuery] = useState<string>("");
  const [value, setValue] = useState<Result | null | undefined>(
    !!title && !!author && !!field.value ? { title: title, author: author, value: field.value } : undefined
  );
  const { isLoading, data, isFetched } = useQuery<Result[]>(
    ["search", searchQuery],
    async () => {
      if (controller.current) {
        controller.current.abort();
      }
      controller.current = new AbortController();

      return await fetchOpenLibrarySearch(searchQuery, controller.current.signal);
    },
    {
      enabled: !!searchQuery,
    }
  );
  const onInputChange = useMemo(() =>
      debounce((event: SyntheticEvent, value: string) => setSearchQuery(value), 400),
      []
  );
  const onChange = (event: SyntheticEvent, value: Result | null | undefined) => {
    field.onChange(value?.value);
    setValue(value);
  };

  return <Autocomplete
      value={value}
      options={!isFetched ? (!!value ? [value] : []) : (data ?? [])}
      isOptionEqualToValue={(option, val) => option?.value === (val?.value || value?.value)}
      onChange={onChange}
      onInputChange={onInputChange}
      getOptionLabel={(option: Result | undefined) => !!option ? `${option.title} - ${option.author}` : "No options"}
      style={{ width: 500 }}
      loading={isLoading}
      renderInput={(params) => (
        <TextInput {...params} {...field} {...props}/>
      )}
  />;
};
BookInput.displayName = "BookInput";
BookInput.defaultProps = { label: "Open Library Book" };
