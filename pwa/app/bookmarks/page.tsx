import {type Metadata} from "next";
import {redirect} from "next/navigation";

import {List, type Props as ListProps} from "../../components/bookmark/List";
import {type Bookmark} from "../../types/Bookmark";
import {type PagedCollection} from "../../types/collection";
import {fetchApi, type FetchResponse} from "../../utils/dataAccess";
import {getServerAccessToken, getServerSession} from "../../lib/auth-helpers";

interface Query extends URLSearchParams {
  page?: number|string|null;
}

export const metadata: Metadata = {
  title: 'Bookmarks',
}
async function getServerSideProps({ page = 1 }: Query, accessToken: string): Promise<ListProps> {
  try {
    const response: FetchResponse<PagedCollection<Bookmark>> | undefined = await fetchApi(`/bookmarks?page=${Number(page)}`, {
      // next: { revalidate: 3600 },
      cache: "no-cache",
    }, accessToken);
    if (!response?.data) {
      throw new Error('Unable to retrieve data from /bookmarks.');
    }

    return { data: response.data, hubURL: response.hubURL, page: Number(page) };
  } catch (error) {
    console.error(error);
  }

  return { data: null, hubURL: null, page: Number(page) };
}

export default async function Page({ searchParams }: { searchParams: Promise<Query> }) {
  const session = await getServerSession();
  const accessToken = await getServerAccessToken();
  if (!session || !accessToken) {
    redirect("/login?callbackURL=/bookmarks");
  }

  const props = await getServerSideProps(await searchParams, accessToken);

  return <List {...props}/>;
}
