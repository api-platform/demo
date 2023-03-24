import { GetStaticProps } from "next";
import { dehydrate, QueryClient } from "react-query";

import {
  PageList,
  getBooks,
  getBooksPath,
} from "../../components/book/PageList";
import { ENTRYPOINT } from "../../config/entrypoint";

export const getStaticProps: GetStaticProps = async () => {
  // prevent failure on build without API available
  if (!ENTRYPOINT) {
    return {
      props: {},
    };
  }

  const queryClient = new QueryClient();
  await queryClient.prefetchQuery(getBooksPath(), getBooks());

  return {
    props: {
      dehydratedState: dehydrate(queryClient),
    },
    revalidate: 1,
  };
};

export default PageList;
