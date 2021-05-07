import { GetServerSideProps, NextComponentType, NextPageContext } from "next";
import { List } from "components/book/List";
import { PagedCollection } from "types/Collection";
import { Book } from "types/Book";
import { fetch } from "utils/dataAccess";
import Head from "next/head";
import Pagination from "components/common/Pagination";
import { useMercure } from "utils/mercure";

interface Props {
  collection: PagedCollection<Book>;
  hubURL: string;
}

const Page: NextComponentType<NextPageContext, Props, Props> = (props) => {
  const collection = useMercure(props.collection, props.hubURL);

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
}

export const getServerSideProps: GetServerSideProps = async ({ resolvedUrl }) => {
  const response = await fetch(resolvedUrl);

  return {
    props: {
      collection: response.data,
      hubURL: response.hubURL,
    },
  }
}

export default Page;
