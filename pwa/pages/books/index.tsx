import { GetStaticProps, NextComponentType, NextPageContext } from "next";
import { List } from "components/book/List";
import { PagedCollection } from "types/Collection";
import { Book } from "types/Book";
import { fetch } from "utils/dataAccess";
import Head from "next/head";

interface Props {
  collection: PagedCollection<Book>;
}

const Page: NextComponentType<NextPageContext, Props, Props> = ({
  collection,
}) => (
  <div>
    <div>
      <Head>
        <title>Book List</title>
      </Head>
    </div>
    <List books={collection["hydra:member"]} />
  </div>
);

export const getStaticProps: GetStaticProps = async () => {
  let collection = [];
  try {
    collection = await fetch("/books");
  } catch (e) {
    console.error(e);
  }

  return {
    props: {
      collection,
    },
    revalidate: 1,
  }
}

export default Page;
