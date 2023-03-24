import { GetStaticPaths, GetStaticProps } from "next";
import { dehydrate, QueryClient } from "react-query";

import {
  PageList,
  getReviews,
  getReviewsPath,
} from "../../../components/review/PageList";
import { PagedCollection } from "../../../types/collection";
import { Review } from "../../../types/Review";
import { fetch, getCollectionPaths } from "../../../utils/dataAccess";
import { ENTRYPOINT } from "../../../config/entrypoint";

export const getStaticProps: GetStaticProps = async ({
  params: { page } = {},
}) => {
  // prevent failure on build without API available
  if (!ENTRYPOINT) {
    return {
      props: {},
    };
  }

  const queryClient = new QueryClient();
  await queryClient.prefetchQuery(getReviewsPath(page), getReviews(page));

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

  const response = await fetch<PagedCollection<Review>>("/reviews");
  const paths = await getCollectionPaths(
    response,
    "reviews",
    "/reviews/page/[page]"
  );

  return {
    paths,
    fallback: true,
  };
};

export default PageList;
