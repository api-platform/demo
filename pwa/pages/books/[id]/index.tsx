import {
  GetStaticPaths,
  GetStaticProps,
  NextComponentType,
  NextPageContext,
} from "next";
import DefaultErrorPage from "next/error";
import Head from "next/head";
import { useRouter } from "next/router";
import { dehydrate, QueryClient, useQuery } from "react-query";

import { Show } from "../../../components/book/Show";
import { PagedCollection } from "../../../types/collection";
import { Book } from "../../../types/Book";
import { fetch, FetchResponse, getItemPaths } from "../../../utils/dataAccess";
import { useMercure } from "../../../utils/mercure";
import { ENTRYPOINT } from "../../../config/entrypoint";

const getBook = async (id: string | string[] | undefined) =>
  id ? await fetch<Book>(`/books/${id}`) : Promise.resolve(undefined);

const Page: NextComponentType<NextPageContext> = () => {
  const router = useRouter();
  const { id } = router.query;

  const { data: { data: book, hubURL, text } = { hubURL: null, text: "" } } =
    useQuery<FetchResponse<Book> | undefined>(["book", id], () => getBook(id));
  const bookData = useMercure(book, hubURL);

  if (!bookData) {
    return <DefaultErrorPage statusCode={404} />;
  }

  return (
    <div>
      <div>
        <Head>
          <title>{`Show Book ${bookData["@id"]}`}</title>
        </Head>
      </div>
      <Show book={bookData} text={text} />
    </div>
  );
};

export const getStaticProps: GetStaticProps = async ({
  params: { id } = {},
}) => {
  // prevent failure on build without API available
  if (!ENTRYPOINT) {
    return {
      props: {},
    };
  }

  if (!id) throw new Error("id not in query param");
  const queryClient = new QueryClient();
  await queryClient.prefetchQuery(["book", id], () => getBook(id));

  return {
    props: {
      dehydratedState: dehydrate(queryClient),
    },
    revalidate: 1,
  };
};

export const getStaticPaths: GetStaticPaths = async () => {
  // prevent failure on build without API available
  if (!ENTRYPOINT) {
    return {
      paths: [],
      fallback: true,
    };
  }

  const response = await fetch<PagedCollection<Book>>("/books");
  const paths = await getItemPaths(response, "books", "/books/[id]");

  return {
    paths,
    fallback: true,
  };
};

export default Page;
