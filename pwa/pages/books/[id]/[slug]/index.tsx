import { GetServerSideProps } from "next";

import { Show } from "@/components/book/Show";
import { Book } from "@/types/Book";
import { type FetchResponse, fetch } from "@/utils/dataAccess";

export const getServerSideProps: GetServerSideProps<{
  data: Book,
  hubURL: string | null,
  page: number, // required for reviews pagination, prevents useRouter
}> = async ({ query: { id, page } }) => {
  try {
    const response: FetchResponse<Book> | undefined = await fetch(`/books/${id}`, {
      headers: {
        Preload: "/books/*/reviews",
      }
    });
    if (!response?.data) {
      throw new Error(`Unable to retrieve data from /books/${id}.`);
    }

    return { props: { data: response.data, hubURL: response.hubURL, page: Number(page ?? 1) } };
  } catch (error) {
    console.error(error);
  }

  return { notFound: true };
};

export default Show;
