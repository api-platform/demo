"use client";

import Head from "next/head";
import {useContext, useRef, useState} from "react";
import {type DataProvider, localStorageStore} from "react-admin";
import {signIn, useSession} from "next-auth/react";
import SyncLoader from "react-spinners/SyncLoader";
import {fetchHydra, HydraAdmin, hydraDataProvider, OpenApiAdmin, ResourceGuesser,} from "@api-platform/admin";
import {parseHydraDocumentation} from "@api-platform/api-doc-parser";

import {type Session} from "../../app/auth";
import DocContext from "../../components/admin/DocContext";
import authProvider from "../../components/admin/authProvider";
import Layout from "./layout/Layout";
import {ENTRYPOINT} from "../../config/entrypoint";
import bookResourceProps from "./book";
import reviewResourceProps from "./review";
import i18nProvider from "./i18nProvider";

const apiDocumentationParser = (session: Session) => async () => {
  try {
    return await parseHydraDocumentation(ENTRYPOINT, {
      headers: {
        Authorization: `Bearer ${session?.accessToken}`,
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
  session,
  children,
}: {
  session: Session;
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
          Authorization: `Bearer ${session?.accessToken}`,
        },
      }),
    apiDocumentationParser: apiDocumentationParser(session),
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

const AdminWithContext = ({ session }: { session: Session }) => {
  const [docType, setDocType] = useState(
    store.getItem<string>("docType", "hydra")
  );

  return (
    // @ts-expect-error Ignore Eslint error
    <DocContext.Provider value={{ docType, setDocType }}>
      <AdminAdapter session={session}>
        <ResourceGuesser name="admin/books" {...bookResourceProps} />
        <ResourceGuesser name="admin/reviews" {...reviewResourceProps} />
      </AdminAdapter>
    </DocContext.Provider>
  );
};

const AdminWithOIDC = () => {
  // Can't use next-auth/middleware because of https://github.com/nextauthjs/next-auth/discussions/7488
  const { data: session, status } = useSession();

  if (status === "loading") {
    return <SyncLoader size={8} color="#46B6BF" />;
  }

  // @ts-expect-error Ignore Eslint error
  if (!session || session?.error === "RefreshAccessTokenError") {
    (async () => await signIn("keycloak"))();

    return;
  }

  // @ts-expect-error Ignore Eslint error
  return <AdminWithContext session={session} />;
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
