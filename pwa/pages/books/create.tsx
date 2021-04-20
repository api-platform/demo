import { NextComponentType, NextPageContext } from "next";
import { Form } from "components/book/Form";
import Head from "next/head";

const Page: NextComponentType<NextPageContext> = () => (
  <div>
    <div>
      <Head>
        <title>Create Book </title>
      </Head>
    </div>
    <Form />
  </div>
);

export default Page;
