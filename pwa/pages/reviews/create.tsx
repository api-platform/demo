import { NextComponentType, NextPageContext } from "next";
import { Form } from "components/review/Form";
import Head from "next/head";

const Page: NextComponentType<NextPageContext> = () => (
  <div>
    <div>
      <Head>
        <title>Create Review </title>
      </Head>
    </div>
    <Form />
  </div>
);

export default Page;
