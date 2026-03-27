"use client";

import Head from "next/head";
import {useContext, useRef, useState} from "react";
import {type DataProvider, localStorageStore} from "react-admin";
import SyncLoader from "react-spinners/SyncLoader";
import {fetchHydra, HydraAdmin, hydraDataProvider, OpenApiAdmin, ResourceGuesser,} from "@api-platform/admin";
import {parseHydraDocumentation} from "@api-platform/api-doc-parser";

import DocContext from "../../components/admin/DocContext";
import authProvider from "../../components/admin/authProvider";
import Layout from "./layout/Layout";
import {ENTRYPOINT} from "../../config/entrypoint";
import bookResourceProps from "./book";
import reviewResourceProps from "./review";
import i18nProvider from "./i18nProvider";
import {useAccessToken, signInWithKeycloak} from "../../hooks/useAuth";

const apiDocumentationParser = (accessToken: string) => async () => {
  try {
    return await parseHydraDocumentation(ENTRYPOINT, {
      headers: {
        Authorization: `Bearer ${accessToken}`,
      },
    });
  } catch (result) {
    // @ts-expect-error Ignore Eslint error
    const { api, response, status } = result;
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

const AdminAdapter = ({
  accessToken,
  children,
}: {
  accessToken: string;
  children?: React.ReactNode | undefined;
}) => {
  // @ts-expect-error Ignore Eslint error
  const dataProvider = useRef<DataProvider>();
  const { docType } = useContext(DocContext);

  dataProvider.current = hydraDataProvider({
    entrypoint: ENTRYPOINT,
    httpClient: (url: URL, options = {}) =>
      fetchHydra(url, {
        ...options,
        headers: {
          ...options.headers,
          Authorization: `Bearer ${accessToken}`,
        },
      }),
    apiDocumentationParser: apiDocumentationParser(accessToken),
  });

  return docType === "hydra" ? (
    <HydraAdmin
      requireAuth
      authProvider={authProvider}
      // @ts-expect-error Ignore Eslint error
      dataProvider={dataProvider.current}
      entrypoint={window.origin}
      i18nProvider={i18nProvider}
      layout={Layout}
    >
      {!!children && children}
    </HydraAdmin>
  ) : (
    <OpenApiAdmin
      requireAuth
      authProvider={authProvider}
      // @ts-expect-error Ignore Eslint error
      dataProvider={dataProvider.current}
      entrypoint={window.origin}
      docEntrypoint={`${window.origin}/docs.json`}
      i18nProvider={i18nProvider}
      layout={Layout}
    >
      {!!children && children}
    </OpenApiAdmin>
  );
};

const store = localStorageStore();

const AdminWithContext = ({ accessToken }: { accessToken: string }) => {
  const [docType, setDocType] = useState(
    store.getItem<string>("docType", "hydra")
  );

  return (
    // @ts-expect-error Ignore Eslint error
    <DocContext.Provider value={{ docType, setDocType }}>
      <AdminAdapter accessToken={accessToken}>
        <ResourceGuesser name="admin/books" {...bookResourceProps} />
        <ResourceGuesser name="admin/reviews" {...reviewResourceProps} />
      </AdminAdapter>
    </DocContext.Provider>
  );
};

const AdminWithOIDC = () => {
  const { accessToken, session, isPending } = useAccessToken();

  if (isPending) {
    return <SyncLoader size={8} color="#46B6BF" />;
  }

  if (!session || !accessToken) {
    (async () => await signInWithKeycloak())();

    return;
  }

  return <AdminWithContext accessToken={accessToken} />;
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
