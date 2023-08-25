import { type NextPage } from "next";
import Head from "next/head";

import { Item } from "@/components/book/Item";
import { Pagination } from "@/components/common/Pagination";
import { type Bookmark } from "@/types/Bookmark";
import { type PagedCollection } from "@/types/collection";
import { useMercure } from "@/utils/mercure";

interface Props {
  data: PagedCollection<Bookmark> | null;
  hubURL: string | null;
  page: number;
}

const getPagePath = (page: number): string => `/bookmarks?page=${page}`;

export const List: NextPage<Props> = ({ data, hubURL, page }) => {
  const collection = useMercure(data, hubURL);

  return (
    <div className="container mx-auto max-w-7xl items-center justify-between p-6 lg:px-8">
      <Head>
        <title>Bookmarks</title>
      </Head>
      {!!collection && !!collection["hydra:member"] && (
        <>
          <p className="w-full text-center px-8 pb-4 text-lg" data-testid="nb-bookmarks">
            {collection["hydra:totalItems"]} book(s) bookmarked
          </p>
          <div className="grid grid-cols-6 gap-4">
            {collection["hydra:member"].length !== 0 && collection["hydra:member"].map((bookmark) => (
              <Item key={bookmark["@id"]} book={bookmark.book}/>
            ))}
          </div>
          <Pagination collection={collection} getPagePath={getPagePath} currentPage={page}/>
        </>
      ) || (
        <p className="w-full flex pb-4 text-lg">No bookmarks found.</p>
      )}
    </div>
  );
};
