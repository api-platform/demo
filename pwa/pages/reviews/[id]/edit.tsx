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

import { Form } from "../../../components/review/Form";
import { PagedCollection } from "../../../types/collection";
import { Review } from "../../../types/Review";
import { fetch, FetchResponse, getPaths } from "../../../utils/dataAccess";
import {ENTRYPOINT} from "../../../config/entrypoint";

const getReview = async (id: string | string[] | undefined) =>
  id ? await fetch<Review>(`/reviews/${id}`) : Promise.resolve(undefined);

const Page: NextComponentType<NextPageContext> = () => {
  const router = useRouter();
  const { id } = router.query;

  const { data: { data: review } = {} } = useQuery<
    FetchResponse<Review> | undefined
  >(["review", id], () => getReview(id));

  if (!review) {
    return <DefaultErrorPage statusCode={404} />;
  }

  return (
    <div>
      <div>
        <Head>
          <title>{review && `Edit Review ${review["@id"]}`}</title>
        </Head>
      </div>
      <Form review={review} />
    </div>
  );
};

export const getStaticProps: GetStaticProps = async ({
  params: { id } = {},
}) => {
  if (!ENTRYPOINT) {
    return {
      props: {},
    };
  }

  if (!id) throw new Error("id not in query param");
  const queryClient = new QueryClient();
  await queryClient.prefetchQuery(["review", id], () => getReview(id));

  return {
    props: {
      dehydratedState: dehydrate(queryClient),
    },
    revalidate: 1,
  };
};

export const getStaticPaths: GetStaticPaths = async () => {
  if (!ENTRYPOINT) {
    return {
      paths: [],
      fallback: true,
    };
  }

  const response = await fetch<PagedCollection<Review>>("/reviews");
  const paths = await getPaths(response, "reviews", "/reviews/[id]/edit");

  return {
    paths,
    fallback: true,
  };
};

export default Page;
