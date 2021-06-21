import Head from "next/head";
import React from "react";
import { Redirect, Route } from "react-router-dom";
import { hydraDataProvider as baseHydraDataProvider, fetchHydra as baseFetchHydra, useIntrospection } from "@api-platform/admin";
import parseHydraDocumentation from "@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation";
import authProvider from "utils/authProvider";
import { ENTRYPOINT } from "config/entrypoint";
import Login from "components/admin/Login";

// todo Waiting for https://github.com/api-platform/admin/issues/372
const getHeaders = () => localStorage.getItem("token") ? {
  Authorization: `Bearer ${localStorage.getItem("token")}`,
} : {};
const fetchHydra = (url, options = {}) =>
  baseFetchHydra(url, {
    ...options,
    headers: getHeaders,
  });
const RedirectToLogin = () => {
  const introspect = useIntrospection();

  if (localStorage.getItem("token")) {
    introspect();
    return <></>;
  }
  return <Redirect to="/login" />;
};
const apiDocumentationParser = async () => {
  try {
    const { api } = await parseHydraDocumentation(ENTRYPOINT, { headers: getHeaders });
    return { api };
  } catch (result) {
    if (result.status !== 401) {
      throw result;
    }

    // Prevent infinite loop if the token is expired
    localStorage.removeItem("token");

    return {
      api: result.api,
      customRoutes: [
        <Route key="/" path="/" component={RedirectToLogin} />
      ],
    };
  }
};
const dataProvider = baseHydraDataProvider(ENTRYPOINT, fetchHydra, apiDocumentationParser);

const AdminLoader = () => {
  if (typeof window !== "undefined") {
    const { HydraAdmin } = require("@api-platform/admin");
    return <HydraAdmin dataProvider={dataProvider} authProvider={authProvider} entrypoint={window.origin} loginPage={Login} />;
  }

  return <></>;
};

const Admin = () => (
  <>
    <Head>
      <title>API Platform Admin</title>
    </Head>

    <AdminLoader />
  </>
);
export default Admin;
