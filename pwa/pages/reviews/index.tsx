import { GetServerSideProps, NextComponentType, NextPageContext } from "next";
import Head from "next/head";
import { dehydrate, QueryClient, useQuery } from "react-query";

import Pagination from "../../components/common/Pagination";
import { List } from "../../components/review/List";
import { PagedCollection } from "../../types/collection";
import { Review } from "../../types/Review";
import { fetch, FetchResponse } from "../../utils/dataAccess";
import { useMercure } from "../../utils/mercure";

const getReviews = async () => await fetch<PagedCollection<Review>>("/reviews");

const Page: NextComponentType<NextPageContext> = () => {
  const { data: { data: reviews, hubURL } = { hubURL: null } } = useQuery<
    FetchResponse<PagedCollection<Review>> | undefined
  >("reviews", getReviews);
  const collection = useMercure(reviews, hubURL);

  if (!collection || !collection["hydra:member"]) return null;

  return (
    <div>
      <div>
        <Head>
          <title>Review List</title>
        </Head>
      </div>
      <List reviews={collection["hydra:member"]} />
      <Pagination collection={collection} />
    </div>
  );
};

export const getServerSideProps: GetServerSideProps = async () => {
  const queryClient = new QueryClient();
  await queryClient.prefetchQuery("reviews", getReviews);

  return {
    props: {
      dehydratedState: dehydrate(queryClient),
    },
  };
};

export default Page;
