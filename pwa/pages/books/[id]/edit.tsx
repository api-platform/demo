import {
  GetStaticPaths,
  GetStaticProps,
  NextComponentType,
  NextPageContext,
} from 'next';
import Head from 'next/head';
import DefaultErrorPage from 'next/error';
import { Form } from 'components/book/Form';
import { Book } from 'types/Book';
import { fetch } from 'utils/dataAccess';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

interface Props {
  book: Book;
}

const Page: NextComponentType<NextPageContext, Props, Props> = ({ book }) => {
  if (!book) {
    return <DefaultErrorPage statusCode={404} />;
  }

  return (
    <div>
      <div>
        <Head>
          <title>{book && `Edit Book ${book['@id']}`}</title>
        </Head>
      </div>
      <Form book={book} />
    </div>
  );
};

export const getStaticProps: GetStaticProps = async ({ params }) => {
  return {
    props: {
      book: (await fetch(`/books/${params.id}`)).data,
    },
    revalidate: 1,
  };
};

export const getStaticPaths: GetStaticPaths = async () => {
  try {
    const response = await fetch('/books');

    return {
      paths: response.data['hydra:member'].map((book) => `${book['@id']}/edit`),
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
