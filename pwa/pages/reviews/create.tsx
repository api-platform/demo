import { NextComponentType, NextPageContext } from "next";
import Head from "next/head";
import { Form } from "components/review/Form";

import "bootstrap/dist/css/bootstrap.css";
import "bootstrap-icons/font/bootstrap-icons.css";

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
