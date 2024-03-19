import { type Metadata } from "next";
import { redirect } from "next/navigation";

import { List, type Props as ListProps } from "../../components/bookmark/List";
import { type Bookmark } from "../../types/Bookmark";
import { type PagedCollection } from "../../types/collection";
import { type FetchResponse, fetchApi } from "../../utils/dataAccess";
import { type Session, auth } from "../auth";

interface Query extends URLSearchParams {
  page?: number|string|null;
}

export const metadata: Metadata = {
  title: 'Bookmarks',
}
async function getServerSideProps({ page = 1 }: Query, session: Session): Promise<ListProps> {
  try {
    const response: FetchResponse<PagedCollection<Bookmark>> | undefined = await fetchApi(`/bookmarks?page=${Number(page)}`, {
      // next: { revalidate: 3600 },
      cache: "no-cache",
    }, session);
    if (!response?.data) {
      throw new Error('Unable to retrieve data from /bookmarks.');
    }

    return { data: response.data, hubURL: response.hubURL, page: Number(page) };
  } catch (error) {
    console.error(error);
  }

  return { data: null, hubURL: null, page: Number(page) };
}

export default async function Page({ searchParams }: { searchParams: Query }) {
  // @ts-ignore
  const session: Session|null = await auth();
  if (!session || session?.error === "RefreshAccessTokenError") {
    // todo find a way to redirect directly to keycloak from here
    // Can't use next-auth/middleware because of https://github.com/nextauthjs/next-auth/discussions/7488
    redirect("/api/auth/signin?callbackUrl=/bookmarks");
  }

  const props = await getServerSideProps(searchParams, session);

  return <List {...props}/>;
}
