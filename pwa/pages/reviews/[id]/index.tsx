import { GetStaticPaths, GetStaticProps, NextComponentType, NextPageContext } from "next";
import { Show } from "components/review/Show";
import { Review } from "types/Review";
import { fetch } from "utils/dataAccess";
import Head from "next/head";
import DefaultErrorPage from "next/error";
import { useMercure } from "utils/mercure";

interface Props {
  review: Review;
  hubURL: string;
}

const Page: NextComponentType<NextPageContext, Props, Props> = (props) => {
  const review = useMercure(props.review, props.hubURL);

  if (!review) {
    return <DefaultErrorPage statusCode={404} />;
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
  const response = await fetch(`/reviews/${params.id}`);

  return {
    props: {
      review: response.data,
      hubURL: response.hubURL,
    },
    revalidate: 1,
  }
}

export const getStaticPaths: GetStaticPaths = async () => {
  try {
    const response = await fetch("/reviews");

    return {
      paths: response.data["hydra:member"].map((review) => review["@id"]),
      fallback: true,
    };
  } catch (e) {
    console.error(e);
  }

  return {
    paths: [],
    fallback: true,
  };
}

export default Page;
