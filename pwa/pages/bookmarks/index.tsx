import { type GetServerSideProps } from "next";
import { getServerSession } from "next-auth/next";

import { List } from "@/components/bookmark/List";
import { type Bookmark } from "@/types/Bookmark";
import { type PagedCollection } from "@/types/collection";
import { type FetchResponse, fetch } from "@/utils/dataAccess";
import { authOptions } from "@/pages/api/auth/[...nextauth]";

export const getServerSideProps: GetServerSideProps<{
  data: PagedCollection<Bookmark> | null,
  hubURL: string | null,
  page: number,
}> = async ({ query: { page }, req, res }) => {
  const session = await getServerSession(req, res, authOptions);
  if (!session) {
    // todo find a way to redirect directly to keycloak from here
    // Can't use next-auth/middleware because of https://github.com/nextauthjs/next-auth/discussions/7488
    return {
      redirect: {
        destination: `/api/auth/signin?callbackUrl=${req.url}`,
        permanent: false
      }
    };
  }

  try {
    const response: FetchResponse<PagedCollection<Bookmark>> | undefined = await fetch(`/bookmarks?page=${Number(page ?? 1)}`, {
      headers: {
        Authorization: `Bearer ${session?.accessToken}`
      }
    });
    if (!response?.data) {
      throw new Error('Unable to retrieve data from /bookmarks.');
    }

    return { props: { data: response.data, hubURL: response.hubURL, page: page ?? 1 } };
  } catch (error) {
    console.error(error);
  }

  return { props: { data: null, hubURL: null, page: page ?? 1 } };
};

export default List;
