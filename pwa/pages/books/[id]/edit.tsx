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

import { Form } from "../../../components/book/Form";
import { PagedCollection } from "../../../types/collection";
import { Book } from "../../../types/Book";
import { fetch, FetchResponse, getPaths } from "../../../utils/dataAccess";

const getBook = async (id: string | string[] | undefined) =>
  id ? await fetch<Book>(`/books/${id}`) : Promise.resolve(undefined);

const Page: NextComponentType<NextPageContext> = () => {
  const router = useRouter();
  const { id } = router.query;

  const { data: { data: book } = {} } = useQuery<
    FetchResponse<Book> | undefined
  >(["book", id], () => getBook(id));

  if (!book) {
    return <DefaultErrorPage statusCode={404} />;
  }

  return (
    <div>
      <div>
        <Head>
          <title>{book && `Edit Book ${book["@id"]}`}</title>
        </Head>
      </div>
      <Form book={book} />
    </div>
  );
};

export const getStaticProps: GetStaticProps = async ({
  params: { id } = {},
}) => {
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
  const response = await fetch<PagedCollection<Book>>("/books");
  const paths = await getPaths(response, "books", "/books/[id]/edit");

  return {
    paths,
    fallback: true,
  };
};

export default Page;
