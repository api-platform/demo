import {type Metadata} from "next";
import {notFound} from "next/navigation";

import {type Props as ShowProps, Show} from "../../../../components/book/Show";
import {Book} from "../../../../types/Book";
import {fetchApi, type FetchResponse} from "../../../../utils/dataAccess";
import {getServerAccessToken} from "../../../../lib/auth-helpers";

interface Props {
  params: Promise<{ id: string }>;
}

export async function generateMetadata({ params }: Props): Promise<Metadata|undefined> {
  const id = (await params).id;
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

async function getServerSideProps(id: string, accessToken: string|null): Promise<ShowProps|undefined> {
  try {
    const response: FetchResponse<Book> | undefined = await fetchApi(`/books/${id}`, {
      headers: {
        Preload: "/books/*/reviews",
      },
      // next: { revalidate: 3600 },
      cache: "no-cache",
    }, accessToken);
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
  const accessToken = await getServerAccessToken();
  const props = await getServerSideProps((await params).id, accessToken);
  if (!props) {
    notFound();
  }

  return <Show {...props}/>;
}
