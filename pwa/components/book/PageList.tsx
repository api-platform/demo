import { NextComponentType, NextPageContext } from "next";
import { useRouter } from "next/router";
import Head from "next/head";
import { useQuery } from "react-query";

import Pagination from "../common/Pagination";
import { List } from "./List";
import { PagedCollection } from "../../types/collection";
import { Book } from "../../types/Book";
import { fetch, FetchResponse, parsePage } from "../../utils/dataAccess";
import { useMercure } from "../../utils/mercure";

export const getBooksPath = (page?: string | string[] | undefined) =>
  `/books${typeof page === "string" ? `?page=${page}` : ""}`;
export const getBooks = (page?: string | string[] | undefined) => async () =>
  await fetch<PagedCollection<Book>>(getBooksPath(page));
const getPagePath = (path: string) => `/books/page/${parsePage("books", path)}`;

export const PageList: NextComponentType<NextPageContext> = () => {
  const {
    query: { page },
  } = useRouter();
  const { data: { data: books, hubURL } = { hubURL: null } } = useQuery<
    FetchResponse<PagedCollection<Book>> | undefined
  >(getBooksPath(page), getBooks(page));
  const collection = useMercure(books, hubURL);

  if (!collection || !collection["hydra:member"]) return null;

  return (
    <div>
      <div>
        <Head>
          <title>Book List</title>
        </Head>
      </div>
      <List books={collection["hydra:member"]} />
      <Pagination collection={collection} getPagePath={getPagePath} />
    </div>
  );
};
