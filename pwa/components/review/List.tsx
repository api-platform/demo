import { type FunctionComponent, useEffect, useState } from "react";
import { signIn, useSession } from "next-auth/react";

import { Pagination } from "@/components/common/Pagination";
import { type Book } from "@/types/Book";
import { type PagedCollection } from "@/types/collection";
import { type Review } from "@/types/Review";
import { fetch, type FetchResponse, getItemPath } from "@/utils/dataAccess";
import { useMercure } from "@/utils/mercure";
import { Error } from "@/components/common/Error";
import { Item } from "@/components/review/Item";
import { Form } from "@/components/review/Form";
import { Loading } from "@/components/common/Loading";

interface Props {
  book: Book;
  page: number;
}

export const List: FunctionComponent<Props> = ({ book, page }) => {
  const { data: session, status } = useSession();
  const [data, setData] = useState<PagedCollection<Review> | null | undefined>();
  const [reload, toggleReload] = useState<boolean>(true);
  const [error, setError] = useState<string | undefined>();
  const [hubURL, setHubURL] = useState<string | undefined>();
  const collection = useMercure(data, hubURL);

  useEffect(() => {
    (async () => {
      try {
        const response: FetchResponse<PagedCollection<Review>> | undefined = await fetch(`${book["reviews"]}?itemsPerPage=5&page=${page}`);
        if (response?.data) {
          setData(response.data);
        }
        if (response && response?.hubURL) {
          setHubURL(response.hubURL);
        }
      } catch (error) {
        console.error(error);
        // @ts-ignore
        setError(error.message);

        return;
      }
    })();
  }, [book, page, status, reload]);

  const getPagePath = (page: number): string =>
    `${getItemPath(book, '/books/[id]/[slug]')}?page=${page}#reviews`;

  return (
    <>
      {/* @ts-ignore */}
      {!!session && !session.error && (
        <div className="mb-10 max-w-4xl mx-auto">
          <div className="mb-5 flex">
            <div className="font-semibold text-gray-600 text-xl w-[50px] h-[50px] px-3 py-1 mr-3 rounded-full bg-gray-200 flex items-center justify-center">
              {(session?.user?.name ?? "John Doe").substring(0, 1)}
            </div>
            <div className="w-full">
              <Form book={book} username={session?.user?.name ?? "John Doe"}
                    onSuccess={(values) => {
                      toggleReload(!reload);
                    }}
              />
            </div>
          </div>
        </div>
      ) || (
        <div className="flex mb-10">
          <button className="px-10 py-4 font-semibold text-sm bg-cyan-500 text-white rounded shadow-sm mx-auto"
                  onClick={() => signIn("keycloak")}>
            Log in to add a review!
          </button>
        </div>
      )}
      {!!error && (
        <Error message={error}/>
      ) || !!collection && !!collection["hydra:member"] && collection["hydra:member"]?.length > 0 && (
        <>
          {collection["hydra:member"].map((review) => (
            <Item key={review["@id"]} review={review} onDelete={() => toggleReload(!reload)}/>
          ))}
          <Pagination collection={collection} getPagePath={getPagePath} currentPage={page}/>
        </>
      ) || !!collection && (
        <p className="text-gray-600">Be the first to add a review!</p>
      ) || (
        <Loading/>
      )}
    </>
  );
}
