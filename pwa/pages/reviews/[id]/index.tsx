import {GetStaticPaths, GetStaticProps, NextComponentType, NextPageContext} from "next";
import { Show } from "components/review/Show";
import { Review } from "types/Review";
import { fetch } from "utils/dataAccess";
import Head from "next/head";
import DefaultErrorPage from "next/error";

interface Props {
  review: Review;
}

const Page: NextComponentType<NextPageContext, Props, Props> = ({ review }) => {
  if (!review) {
    return <>
      <DefaultErrorPage statusCode={404} />
    </>
  }

  return (
    <div>
      <div>
        <Head>
          <title>{`Show Review ${review["@id"]}`}</title>
        </Head>
      </div>
      <Show review={review} />
    </div>
  );
};

export const getStaticProps: GetStaticProps = async ({ params }) => {
  const review = await fetch(`/reviews/${params.id}`);

  return {
    props: {
      review,
    },
    revalidate: 1,
  }
}

export const getStaticPaths: GetStaticPaths = async () => {
  return {
    paths: [],
    fallback: true,
  }
}

export default Page;
