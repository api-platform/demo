import { GetStaticProps, NextComponentType, NextPageContext } from "next";
import { List } from "components/review/List";
import { PagedCollection } from "types/Collection";
import { Review } from "types/Review";
import { fetch } from "utils/dataAccess";
import Head from "next/head";

interface Props {
  collection: PagedCollection<Review>;
  hubURL: string;
}

const Page: NextComponentType<NextPageContext, Props, Props> = ({ collection, hubURL }) => (
  <div>
    <div>
      <Head>
        <title>Review List</title>
      </Head>
    </div>
    <List reviews={collection["hydra:member"]} hubURL={hubURL} />
  </div>
);

export const getStaticProps: GetStaticProps = async () => {
  try {
    const response = await fetch("/reviews");

    return {
      props: {
        collection: response.data,
        hubURL: response.hubURL,
      },
      revalidate: 1,
    };
  } catch (e) {
    console.error(e);
  }

  return {
    props: {
      collection: [],
      hubURL: null,
    },
    revalidate: 1,
  };
}

export default Page;
