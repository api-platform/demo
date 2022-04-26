import {
  GetStaticPaths,
  GetStaticProps,
  NextComponentType,
  NextPageContext,
} from 'next';
import Head from 'next/head';
import DefaultErrorPage from 'next/error';
import { Form } from 'components/review/Form';
import { Review } from 'types/Review';
import { fetch } from 'utils/dataAccess';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

interface Props {
  review: Review;
}

const Page: NextComponentType<NextPageContext, Props, Props> = ({ review }) => {
  if (!review) {
    return <DefaultErrorPage statusCode={404} />;
  }

  return (
    <div>
      <div>
        <Head>
          <title>{review && `Edit Review ${review['@id']}`}</title>
        </Head>
      </div>
      <Form review={review} />
    </div>
  );
};

export const getStaticProps: GetStaticProps = async ({ params }) => {
  return {
    props: {
      review: (await fetch(`/reviews/${params.id}`)).data,
    },
    revalidate: 1,
  };
};

export const getStaticPaths: GetStaticPaths = async () => {
  try {
    const response = await fetch('/reviews');

    return {
      paths: response.data['hydra:member'].map(
        (review) => `${review['@id']}/edit`,
      ),
      fallback: true,
    };
  } catch (e) {
    console.error(e);
  }

  return {
    paths: [],
    fallback: true,
  };
};

export default Page;
