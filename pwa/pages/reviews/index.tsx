import { GetStaticProps, NextComponentType, NextPageContext } from "next";
import { List } from "components/review/List";
import { PagedCollection } from "types/Collection";
import { Review } from "types/Review";
import { fetch } from "utils/dataAccess";
import Head from "next/head";

interface Props {
  collection: PagedCollection<Review>;
}

const Page: NextComponentType<NextPageContext, Props, Props> = ({
  collection,
}) => (
  <div>
    <div>
      <Head>
        <title>Review List</title>
      </Head>
    </div>
    <List reviews={collection["hydra:member"]} />
  </div>
);

export const getStaticProps: GetStaticProps = async () => {
  let collection = [];
  try {
    collection = await fetch("/reviews");
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
