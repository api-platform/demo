import { NextComponentType, NextPageContext } from "next";
import { useRouter } from "next/router";
import Head from "next/head";
import { useQuery } from "react-query";

import Pagination from "../common/Pagination";
import { List } from "./List";
import { PagedCollection } from "../../types/collection";
import { Review } from "../../types/Review";
import { fetch, FetchResponse, parsePage } from "../../utils/dataAccess";
import { useMercure } from "../../utils/mercure";

export const getReviewsPath = (page?: string | string[] | undefined) =>
  `/reviews${typeof page === "string" ? `?page=${page}` : ""}`;
export const getReviews = (page?: string | string[] | undefined) => async () =>
  await fetch<PagedCollection<Review>>(getReviewsPath(page));
const getPagePath = (path: string) =>
  `/reviews/page/${parsePage("reviews", path)}`;

export const PageList: NextComponentType<NextPageContext> = () => {
  const {
    query: { page },
  } = useRouter();
  const { data: { data: reviews, hubURL } = { hubURL: null } } = useQuery<
    FetchResponse<PagedCollection<Review>> | undefined
  >(getReviewsPath(page), getReviews(page));
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
      <Pagination collection={collection} getPagePath={getPagePath} />
    </div>
  );
};
