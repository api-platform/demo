import {type Metadata} from "next";

import {getServerAccessToken} from "../../lib/auth-helpers";
import {List, type Props as ListProps} from "../../components/book/List";
import {type Book} from "../../types/Book";
import {type PagedCollection} from "../../types/collection";
import {fetchApi, type FetchResponse} from "../../utils/dataAccess";
import {buildUriFromFilters, type FiltersProps} from "../../utils/book";

interface Query extends URLSearchParams {
  page?: number|string|undefined;
  author?: string|undefined;
  title?: string|undefined;
  condition?: string|undefined;
  "condition[]"?: string|string[]|undefined;
  "order[title]"?: string|undefined;
}

interface Props {
  searchParams: Promise<Query>;
}

async function getServerSideProps(query: Query, accessToken: string|null): Promise<ListProps> {
  const page = Number(query.page ?? 1);
  const filters: FiltersProps = {};
  if (query.page) {
    filters.page = page;
  }
  if (query.author) {
    filters.author = query.author;
  }
  if (query.title) {
    filters.title = query.title;
  }
  if (query.condition) {
    filters.condition = [query.condition];
  } else if (typeof query["condition[]"] === "string") {
    filters.condition = [query["condition[]"]];
  } else if (typeof query["condition[]"] === "object") {
    filters.condition = query["condition[]"];
  }
  if (query["order[title]"]) {
    filters.order = { title: query["order[title]"] };
  }

  try {
    const response: FetchResponse<PagedCollection<Book>> | undefined = await fetchApi(buildUriFromFilters("/books", filters), {
      // next: { revalidate: 3600 },
      cache: "no-cache",
    }, accessToken);
    if (!response?.data) {
      throw new Error('Unable to retrieve data from /books.');
    }

    return { data: response.data, hubURL: response.hubURL, filters, page };
  } catch (error) {
    console.error(error);
  }

  return { data: null, hubURL: null, filters, page };
}

export const metadata: Metadata = {
  title: 'Books Store',
}
export default async function Page({ searchParams }: Props) {
  const accessToken = await getServerAccessToken();
  const props = await getServerSideProps(await searchParams, accessToken);

  return <List {...props}/>;
}
