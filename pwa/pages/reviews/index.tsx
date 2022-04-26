import { GetServerSideProps, NextComponentType, NextPageContext } from 'next';
import Head from 'next/head';
import { List } from 'components/review/List';
import { PagedCollection } from 'types/Collection';
import { Review } from 'types/Review';
import { fetch } from 'utils/dataAccess';
import Pagination from 'components/common/Pagination';
import { useMercure } from 'utils/mercure';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

interface Props {
  collection: PagedCollection<Review>;
  hubURL: string;
}

const Page: NextComponentType<NextPageContext, Props, Props> = (props) => {
  const collection = useMercure(props.collection, props.hubURL);

  return (
    <div>
      <div>
        <Head>
          <title>Review List</title>
        </Head>
      </div>
      <List reviews={collection['hydra:member']} />
      <Pagination collection={collection} />
    </div>
  );
};

export const getServerSideProps: GetServerSideProps = async ({
  resolvedUrl,
}) => {
  const response = await fetch(resolvedUrl);

  return {
    props: {
      collection: response.data,
      hubURL: response.hubURL,
    },
  };
};

export default Page;
