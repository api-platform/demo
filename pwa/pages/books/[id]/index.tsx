import { GetStaticPaths, GetStaticProps, NextComponentType, NextPageContext } from "next";
import { Show } from "components/book/Show";
import { Book } from "types/Book";
import { fetch } from "utils/dataAccess";
import Head from "next/head";
import DefaultErrorPage from "next/error";
import { useMercure } from "utils/mercure";

interface Props {
  book: Book;
  hubURL: string;
}

const Page: NextComponentType<NextPageContext, Props, Props> = (props) => {
  const book = useMercure(props.book, props.hubURL);

  if (!book) {
    return <DefaultErrorPage statusCode={404} />;
  }

  return (
    <div>
      <div>
        <Head>
          <title>{`Show Book ${book["@id"]}`}</title>
        </Head>
      </div>
      <Show book={book} />
    </div>
  );
};

export const getStaticProps: GetStaticProps = async ({ params }) => {
  const response = await fetch(`/books/${params.id}`);

  return {
    props: {
      book: response.data,
      hubURL: response.hubURL,
    },
    revalidate: 1,
  }
}

export const getStaticPaths: GetStaticPaths = async () => {
  try {
    const response = await fetch("/books");

    return {
      paths: response.data["hydra:member"].map((book) => book["@id"]),
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
