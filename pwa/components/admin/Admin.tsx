import Head from "next/head";
import { useState } from "react";
import { Navigate, Route } from "react-router-dom";
import { CustomRoutes, Login, LoginClasses, defaultTheme} from "react-admin";
import {
  fetchHydra as baseFetchHydra,
  HydraAdmin,
  hydraDataProvider as baseHydraDataProvider,
  useIntrospection
} from "@api-platform/admin";
import { parseHydraDocumentation } from "@api-platform/api-doc-parser";
import { LoginForm } from "./LoginForm";
import authProvider from "utils/authProvider";
import { ENTRYPOINT } from "config/entrypoint";

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
  return <Navigate to="/login" />;
};
const apiDocumentationParser = (setRedirectToLogin) => async () => {
  try {
    setRedirectToLogin(false);

    return await parseHydraDocumentation(ENTRYPOINT, { headers: getHeaders });
  } catch (result) {
    const { api, response, status } = result;
    if (status !== 401 || !response) {
      throw result;
    }

    // Prevent infinite loop if the token is expired
    localStorage.removeItem("token");

    setRedirectToLogin(true);

    return {
      api,
      response,
      status,
    };
  }
};
const dataProvider = (setRedirectToLogin) => baseHydraDataProvider({
  useEmbedded: false,
  entrypoint: ENTRYPOINT,
  httpClient: fetchHydra,
  apiDocumentationParser: apiDocumentationParser(setRedirectToLogin),
});

const LoginPage = () => (
  <Login
    sx={{
      backgroundImage: 'radial-gradient(circle at 50% 14em, #90dfe7 0%, #288690 60%, #288690 100%)',
      [`& .${LoginClasses.icon}`]: {
        backgroundColor: 'secondary.main',
      },
    }}
  >
    <LoginForm />
  </Login>
)

const Admin = () => {
  const [redirectToLogin, setRedirectToLogin] = useState(false);

  return (
    <>
      <Head>
        <title>API Platform Admin</title>
      </Head>

      <HydraAdmin dataProvider={dataProvider(setRedirectToLogin)} authProvider={authProvider} entrypoint={window.origin} loginPage={LoginPage}>
        <CustomRoutes>
          {redirectToLogin ? <Route path="/" element={<RedirectToLogin />} /> : null}
        </CustomRoutes>
      </HydraAdmin>
    </>
  );
}
export default Admin;
