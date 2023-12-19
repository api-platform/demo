"use client";

import { type NextPage } from "next";
import Image from "next/image";
import Link from "next/link";
import { signIn, useSession } from "next-auth/react";
import { useEffect, useState } from "react";
import { useMutation } from "@tanstack/react-query";
import Typography from "@mui/material/Typography";
import Breadcrumbs from "@mui/material/Breadcrumbs";
import Rating from '@mui/material/Rating';
import FavoriteBorderIcon from "@mui/icons-material/FavoriteBorder";
import FavoriteIcon from "@mui/icons-material/Favorite";

import { type Book } from "../../types/Book";
import { useMercure } from "../../utils/mercure";
import { List as Reviews } from "../../components/review/List";
import { useOpenLibraryBook } from "../../utils/book";
import { fetchApi, type FetchResponse } from "../../utils/dataAccess";
import { type Bookmark } from "../../types/Bookmark";
import { type PagedCollection } from "../../types/collection";
import { Loading } from "../common/Loading";

export interface Props {
  data: Book;
  hubURL: string | null;
}

interface BookmarkProps {
  book: string | undefined;
}

// @ts-ignore
const saveBookmark = async (values: BookmarkProps, session) => await fetchApi<Bookmark>("/bookmarks", {
  method: "POST",
  body: JSON.stringify(values),
}, session);

// @ts-ignore
const deleteBookmark = async (id: string, session) => await fetchApi<Bookmark>(id, { method: "DELETE" }, session);

export const Show: NextPage<Props> = ({ data, hubURL }) => {
  const { data: session, status } = useSession();
  const [bookmark, setBookmark] = useState<Bookmark | null | undefined>();
  const { data: book, isLoading } = useOpenLibraryBook(data);
  const item = useMercure(data, hubURL);

  const bookmarkMutation = useMutation({
    mutationFn: async (data: BookmarkProps) => {
      // @ts-ignore
      if (!session || session?.error === "RefreshAccessTokenError") {
        await signIn("keycloak");

        return;
      }

      if (bookmark) {
        // @ts-ignore
        await deleteBookmark(bookmark["@id"], session);
        setBookmark(null);

        return;
      }

      const response: FetchResponse<Bookmark> | undefined = await saveBookmark(data, session);
      if (response && response?.data) {
        setBookmark(response.data);
      }
    },
    onError: (error) => {
      console.error(error);
    },
  });

  // Check in user bookmarks if the current book has been bookmarked
  useEffect(() => {
    // /bookmarks endpoint requires authentication
    if (status === "loading" || status === "unauthenticated") return;

    (async () => {
      try {
        // @ts-ignore
        const response: FetchResponse<PagedCollection<Bookmark>> | undefined = await fetchApi(`/bookmarks?book=${data["@id"]}`, {}, session);
        if (response && response?.data && response.data["hydra:member"]?.length) {
          setBookmark(response.data["hydra:member"][0]);
        }
      } catch (error) {
        console.error(error);
        setBookmark(null);
      }
    })()
  }, [data, status]);

  return (
    <div className="container mx-auto max-w-7xl items-center justify-between p-6 lg:px-8">
      <div role="presentation" className="mb-8">
        <Breadcrumbs aria-label="breadcrumb" data-testid="book-breadcrumb">
          <Link href="/books" className="hover:underline">
            Books Store
          </Link>
          <Link href={`/books?author=${item["author"]}`} className="hover:underline">
            {item["author"]}
          </Link>
          <Typography color="text.primary">{item["title"]}</Typography>
        </Breadcrumbs>
      </div>
      {!!book && !isLoading && (
        <>
          <div className="flex">
            <div className="min-w-[270px] max-w-[300px] w-full mr-10 text-center">
              {!!book["images"] && (
                <Image alt={book["title"]} width={300} height={300} src={book["images"]["large"]} priority={true} data-testid="book-cover"/>
              ) || (
                <span className="h-40 text-slate-300">No cover</span>
              )}
            </div>
            <div className="w-full">
              <h1 className="font-bold text-2xl text-gray-700">{book["title"]}</h1>
              <h2>
                <Link href={`/books?author=${book["author"]}`} className="text-lg hover:underline">
                  {book["author"]}
                </Link>
              </h2>
              <p className="text-gray-600 mt-4" data-testid="book-metadata">
                <span className="flex">
                  <span>Condition: {book["condition"].replace(/https:\/\/schema\.org\/(.+)Condition$/, "$1")}</span>
                  {!!book["publicationDate"] && (
                    <span className="ml-1"> | Published on {book["publicationDate"]}</span>
                  )}
                </span>
                <span className="mt-2 flex">
                  <span>Average rating</span>
                  <Rating value={book["rating"] ?? 0} readOnly className="ml-2"/>
                  <Link href="#reviews" className="ml-4 hover:underline">Add a review</Link>
                </span>
              </p>
              <p className="text-justify leading-7 my-8" data-testid="book-description">
                {book["description"] ?? "This book has no description."}
              </p>
              <button className="mt-4 px-10 py-4 font-semibold text-sm bg-cyan-500 text-white rounded-full shadow-sm"
                      onClick={() => bookmarkMutation.mutate({ book: book["@id"] })} data-testid="bookmark">
                {!!bookmark && (
                  <>
                    <FavoriteIcon className="w-6 h-6 mr-2"/>
                    Bookmarked
                  </>
                ) || (
                  <>
                    <FavoriteBorderIcon className="w-6 h-6 mr-2"/>
                    Bookmark
                  </>
                )}
              </button>
            </div>
          </div>
          <div className="w-full border-gray-200 border-t-2 mt-8 pt-8" id="reviews">
            <Reviews book={book}/>
          </div>
        </>
      ) || (
        <Loading/>
      )}
    </div>
  );
};
