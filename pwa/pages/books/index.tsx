import { GetServerSideProps, NextComponentType, NextPageContext } from "next";
import Head from "next/head";
import { dehydrate, QueryClient, useQuery } from "react-query";

import Pagination from "../../components/common/Pagination";
import { List } from "../../components/book/List";
import { PagedCollection } from "../../types/collection";
import { Book } from "../../types/Book";
import { fetch, FetchResponse } from "../../utils/dataAccess";
import { useMercure } from "../../utils/mercure";

const getBooks = async () => await fetch<PagedCollection<Book>>("/books");

const Page: NextComponentType<NextPageContext> = () => {
  const { data: { data: books, hubURL } = { hubURL: null } } = useQuery<
    FetchResponse<PagedCollection<Book>> | undefined
  >("books", getBooks);
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
      <Pagination collection={collection} />
    </div>
  );
};

export const getServerSideProps: GetServerSideProps = async () => {
  const queryClient = new QueryClient();
  await queryClient.prefetchQuery("books", getBooks);

  return {
    props: {
      dehydratedState: dehydrate(queryClient),
    },
  };
};

export default Page;
