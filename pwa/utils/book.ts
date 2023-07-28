import slugify from "slugify";
import { isItem } from "@/types/item";
import { type Book } from "@/types/Book";
import { type Book as OLBook } from "@/types/OpenLibrary/Book";
import { type Work } from "@/types/OpenLibrary/Work";

export interface FiltersProps {
  author?: string | undefined;
  title?: string | undefined;
  condition?: string | string[] | undefined;
}

export const populateBook = async <TData extends Book>(book: TData): Promise<TData> => {
  if (!isItem(book)) {
    console.error("Object sent is not in JSON-LD format.");

    return book;
  }

  book["id"] = book["@id"]?.replace("/books/", "");
  book["slug"] = slugify(`${book["title"]}-${book["author"]}`, { lower: true, trim: true, remove: /[*+~.(),;'"!:@]/g });
  book["condition"] = book["condition"].substring(19, book["condition"].length-9);

  const response = await fetch(book["book"], { method: "GET" });
  const data: OLBook = await response.json();

  if (typeof data["publish_date"] !== "undefined") {
    book["publicationDate"] = data["publish_date"];
  }

  if (typeof data["covers"] !== "undefined") {
    book["images"] = {
      medium: `https://covers.openlibrary.org/b/id/${data["covers"][0]}-M.jpg`,
      large: `https://covers.openlibrary.org/b/id/${data["covers"][0]}-L.jpg`,
    };
  }

  if (typeof data["description"] !== "undefined") {
    book["description"] = (typeof data["description"] === "string" ? data["description"] : data["description"]["value"]).replace( /(<([^>]+)>)/ig, '');
  }

  // retrieve data from work if necessary
  if ((!book["description"] || !book["images"]) && typeof data["works"] !== "undefined" && data["works"].length > 0) {
    const response = await fetch(`https://openlibrary.org${data["works"][0]["key"]}.json`);
    const work: Work = await response.json();

    if (!book["description"] && typeof work["description"] !== "undefined") {
      book["description"] = (typeof work["description"] === "string" ? work["description"] : work["description"]["value"]).replace( /(<([^>]+)>)/ig, '');
    }

    if (!book["images"] && typeof work["covers"] !== "undefined") {
      book["images"] = {
        medium: `https://covers.openlibrary.org/b/id/${work["covers"][0]}-M.jpg`,
        large: `https://covers.openlibrary.org/b/id/${work["covers"][0]}-L.jpg`,
      };
    }
  }

  return book;
};

export const buildUriFromFilters = (uri: string, filters: FiltersProps, page: number | undefined = undefined): string => {
  // remove empty filters
  filters = Object.fromEntries(Object.entries(filters).filter(([, value]) => value?.length > 0));

  const params = new URLSearchParams();
  Object.keys(filters).forEach((filter: string) => {
    // @ts-ignore
    const value = filters[filter];
    if (typeof value === "string") {
      params.append(filter, value);
    } else if (typeof value === "object") {
      value.forEach((v: string) => {
        params.append(`${filter}[]`, v);
      })
    }
  });
  if (page) {
    // @ts-ignore
    params.append("page", page);
  }

  return `${uri}${params.size === 0 ? "" : `?${params.toString()}`}`;
};
