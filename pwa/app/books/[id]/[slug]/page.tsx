import { type Metadata } from "next";
import { notFound } from "next/navigation";

import { Show, type Props as ShowProps } from "../../../../components/book/Show";
import { Book } from "../../../../types/Book";
import { type FetchResponse, fetchApi } from "../../../../utils/dataAccess";
import { type Session, auth } from "../../../auth";

interface Props {
  params: { id: string };
}

export async function generateMetadata({ params }: Props): Promise<Metadata|undefined> {
  const id = params.id;
  try {
    const response: FetchResponse<Book> | undefined = await fetchApi(`/books/${id}`, {
      // next: { revalidate: 3600 },
      cache: "no-cache",
    });
    if (!response?.data) {
      throw new Error(`Unable to retrieve data from /books/${id}.`);
    }
    const item = response.data;

    return {
      title: `${item["title"]}${!!item["author"] && ` - ${item["author"]}`}`,
    };
  } catch (error) {
    console.error(error);
  }

  return undefined;
}

async function getServerSideProps(id: string, session: Session|null): Promise<ShowProps|undefined> {
  try {
    const response: FetchResponse<Book> | undefined = await fetchApi(`/books/${id}`, {
      headers: {
        Preload: "/books/*/reviews",
      },
      // next: { revalidate: 3600 },
      cache: "no-cache",
    }, session);
    if (!response?.data) {
      throw new Error(`Unable to retrieve data from /books/${id}.`);
    }

    return { data: response.data, hubURL: response.hubURL };
  } catch (error) {
    console.error(error);
  }

  return undefined;
}

export default async function Page({ params }: Props) {
  // @ts-ignore
  const session: Session|null = await auth();
  const props = await getServerSideProps(params.id, session);
  if (!props) {
    notFound();
  }

  return <Show {...props}/>;
}
