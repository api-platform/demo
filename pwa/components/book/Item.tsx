import Image from "next/image";
import Link from "next/link";
import { type FunctionComponent } from "react";
import Rating from "@mui/material/Rating";

import { type Book } from "@/types/Book";
import { getItemPath } from "@/utils/dataAccess";
import { useOpenLibraryBook } from "@/utils/book";
import { Loading } from "@/components/common/Loading";

interface Props {
  book: Book;
}

export const Item: FunctionComponent<Props> = ({ book }) => {
  const { data, isLoading } = useOpenLibraryBook(book);

  if (isLoading || !data) return <Loading/>;

  return (
    <div className="relative p-4 bg-white hover:drop-shadow-xl border-b border-gray-200 text-center" data-testid="book">
      <div className="h-40 mb-2">
        <Link href={getItemPath(data, "/books/[id]/[slug]")}>
          {!!data["images"] && (
            <Image alt={data["title"]} width={100} height={130} src={data["images"]["medium"]}
                   className="mx-auto w-auto max-w-[150px] h-auto max-h-[165px]" priority={true}
            />
          ) || (
            <span className="text-slate-300 block h-full">No cover</span>
          )}
        </Link>
      </div>
      <div className="h-32 mb-2">
        <p>
          <Link href={getItemPath(data, "/books/[id]/[slug]")}
                className="font-bold text-lg text-gray-700 hover:underline">
            {data["title"]}
          </Link>
        </p>
        <p>
          <Link href={`/books?author=${data["author"]}`} className="hover:underline">
            {data["author"]}
          </Link>
        </p>
        {!!data["rating"] && (
          <Rating value={data["rating"]} readOnly className="ml-2"/>
        )}
      </div>
    </div>
  );
};
