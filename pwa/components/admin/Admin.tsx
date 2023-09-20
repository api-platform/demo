import Head from "next/head";
import React, { useContext, useEffect, useRef, useState } from "react";
import {
  DataProvider,
  Layout,
  LayoutProps,
  localStorageStore,
  resolveBrowserLocale,
} from "react-admin";
import polyglotI18nProvider from "ra-i18n-polyglot";
import englishMessages from "ra-language-english";
import frenchMessages from "ra-language-french";
import {
  fetchHydra,
  HydraAdmin,
  hydraDataProvider,
  OpenApiAdmin
} from "@api-platform/admin";
import { parseHydraDocumentation } from "@api-platform/api-doc-parser";

import DocContext from "./DocContext";
import AppBar from "./AppBar";
import { ENTRYPOINT } from "../../config/entrypoint";
import { getSession, signIn, useSession } from "next-auth/react";

const getHeaders = async () => {
  const session = await getSession();

  return {
    // @ts-ignore
    Authorization: `Bearer ${session?.accessToken}`,
  };
};

const apiDocumentationParser = () => async () => {
  try {
    // @ts-ignore
    return await parseHydraDocumentation(ENTRYPOINT, { headers: getHeaders() });
  } catch (result) {
    // @ts-ignore
    const {api, response, status} = result;
    if (status !== 401 || !response) {
      throw result;
    }

    return {
      api,
      response,
      status,
    };
  }
};

const messages = {
  fr: frenchMessages,
  en: englishMessages,
};
const i18nProvider = polyglotI18nProvider(
  // @ts-ignore
  (locale) => (messages[locale] ? messages[locale] : messages.en),
  resolveBrowserLocale(),
);

const MyLayout = (props: React.JSX.IntrinsicAttributes & LayoutProps) => <Layout {...props} appBar={AppBar} />;

const AdminUI = () => {
  // @ts-ignore
  const dataProvider = useRef<DataProvider>(undefined);
  const { docType } = useContext(DocContext);

  dataProvider.current = hydraDataProvider({
    useEmbedded: false,
    entrypoint: ENTRYPOINT,
    httpClient: (url: URL, options = {}) => fetchHydra(url, {
      ...options,
      // @ts-ignore
      headers: getHeaders(),
    }),
    apiDocumentationParser: apiDocumentationParser(),
  });

  return docType === 'hydra' ? (
    <HydraAdmin
      // @ts-ignore
      dataProvider={dataProvider.current}
      entrypoint={window.origin}
      i18nProvider={i18nProvider}
      layout={MyLayout}
    />
  ) : (
    <OpenApiAdmin
      // @ts-ignore
      dataProvider={dataProvider.current}
      entrypoint={window.origin}
      docEntrypoint={`${window.origin}/docs.json`}
      i18nProvider={i18nProvider}
      layout={MyLayout}
    />
  );
};

const store = localStorageStore();

const AdminWithContext = () => {
  const [docType, setDocType] = useState(
    store.getItem<string>('docType', 'hydra'),
  );

  return (
    <DocContext.Provider
      value={{
        docType,
        setDocType,
      }}>
      <AdminUI />
    </DocContext.Provider>
  );
};

const AdminWithOIDC = () => {
  // Can't use next-auth/middleware because of https://github.com/nextauthjs/next-auth/discussions/7488
  const { status } = useSession();

  useEffect(() => {
    if (status === "unauthenticated") {
      signIn('keycloak');
    }
  }, [status]);

  if (status === "loading") return <p>Loading...</p>;

  return <AdminWithContext />;
};

const Admin = () => (
  <>
    <Head>
      <title>API Platform Admin</title>
    </Head>

    <AdminWithOIDC />
  </>
);

export default Admin;
