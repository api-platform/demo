import { GetStaticPaths, GetStaticProps, NextComponentType, NextPageContext } from "next";
import { Form } from "components/book/Form";
import { Book } from "types/Book";
import { fetch } from "utils/dataAccess";
import Head from "next/head";
import DefaultErrorPage from "next/error";

interface Props {
  book: Book;
}

const Page: NextComponentType<NextPageContext, Props, Props> = ({ book }) => {
  if (!book) {
    return <>
      <DefaultErrorPage statusCode={404} />
    </>
  }

  return (
    <div>
      <div>
        <Head>
          <title>{book && `Edit Book ${book["@id"]}`}</title>
        </Head>
      </div>
      <Form book={book} />
    </div>
  );
};

export const getStaticProps: GetStaticProps = async ({ params }) => {
  const response = { hubURL: null };
  const book = await fetch(`/books/${params.id}`, {}, response);

  return {
    props: {
      book,
      hubURL: response.hubURL,
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
