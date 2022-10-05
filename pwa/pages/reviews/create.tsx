import { NextComponentType, NextPageContext } from "next";
import Head from "next/head";

import { Form } from "../../components/review/Form";

const Page: NextComponentType<NextPageContext> = () => (
  <div>
    <div>
      <Head>
        <title>Create Review</title>
      </Head>
    </div>
    <Form />
  </div>
);

export default Page;
