import slugify from "slugify";
import { useQuery } from "react-query";

import { isItem } from "@/types/item";
import { type Book } from "@/types/Book";
import { type Book as OLBook } from "@/types/OpenLibrary/Book";
import { type Work } from "@/types/OpenLibrary/Work";

interface OrderFilter {
  title: string;
}

export interface FiltersProps {
  author?: string | undefined;
  title?: string | undefined;
  condition?: string | string[] | undefined;
  order?: OrderFilter | undefined;
  page?: number | undefined;
}

export const useOpenLibraryBook = <TData extends Book>(data: TData) => {
  if (!isItem(data)) {
    throw new Error("Object sent is not in JSON-LD format.");
  }

  data["id"] = data["@id"]?.replace("/books/", "");
  data["slug"] = slugify(`${data["title"]}-${data["author"]}`, { lower: true, trim: true, remove: /[*+~.(),;'"!:@]/g });
  data["condition"] = data["condition"].substring(19, data["condition"].length-9);

  return useQuery(data["book"], async () => {
    const response = await fetch(data["book"], { method: "GET" });
    const book: OLBook = await response.json();

    if (typeof book["publish_date"] !== "undefined") {
      data["publicationDate"] = book["publish_date"];
    }

    if (typeof book["covers"] !== "undefined") {
      data["images"] = {
        medium: `https://covers.openlibrary.org/b/id/${book["covers"][0]}-M.jpg`,
        large: `https://covers.openlibrary.org/b/id/${book["covers"][0]}-L.jpg`,
      };
    }

    if (typeof book["description"] !== "undefined") {
      data["description"] = (typeof book["description"] === "string" ? book["description"] : book["description"]["value"]).replace( /(<([^>]+)>)/ig, '');
    }

    // retrieve data from work if necessary
    if ((!data["description"] || !data["images"]) && typeof book["works"] !== "undefined" && book["works"].length > 0) {
      const response = await fetch(`https://openlibrary.org${book["works"][0]["key"]}.json`);
      const work: Work = await response.json();

      if (!data["description"] && typeof work["description"] !== "undefined") {
        data["description"] = (typeof work["description"] === "string" ? work["description"] : work["description"]["value"]).replace( /(<([^>]+)>)/ig, '');
      }

      if (!data["images"] && typeof work["covers"] !== "undefined") {
        data["images"] = {
          medium: `https://covers.openlibrary.org/b/id/${work["covers"][0]}-M.jpg`,
          large: `https://covers.openlibrary.org/b/id/${work["covers"][0]}-L.jpg`,
        };
      }
    }

    return data;
  });
};

const filterObject = (object: object) => Object.fromEntries(Object.entries(object).filter(([, value]) => {
  return typeof value === "object" ? Object.keys(value).length > 0 : value?.length > 0;
}));

export const buildUriFromFilters = (uri: string, filters: FiltersProps): string => {
  // remove empty filters
  filters = filterObject(filters);

  const params = new URLSearchParams();
  Object.keys(filters).forEach((filter: string) => {
    // @ts-ignore
    const value = filters[filter];
    if (typeof value === "string" || typeof value === "number") {
      params.append(filter, value.toString());
    } else if (Array.isArray(value)) {
      value.forEach((v: string) => {
        params.append(`${filter}[]`, v);
      });
    } else if (typeof value === "object") {
      // @ts-ignore
      Object.entries(value).forEach(([k, v]) => params.append(`${filter}[${k}]`, v));
    }
  });

  return `${uri}${params.size === 0 ? "" : `?${params.toString()}`}`;
};
