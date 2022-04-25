import { useContext, useState } from 'react';
import Head from 'next/head';
import { Navigate, Route } from 'react-router-dom';
import {
  CustomRoutes,
  Layout,
  localStorageStore,
  Login,
  LoginClasses,
  resolveBrowserLocale,
} from 'react-admin';
import polyglotI18nProvider from 'ra-i18n-polyglot';
import englishMessages from 'ra-language-english';
import frenchMessages from 'ra-language-french';
import {
  fetchHydra as baseFetchHydra,
  HydraAdmin,
  hydraDataProvider as baseHydraDataProvider,
  OpenApiAdmin,
  useIntrospection,
} from '@api-platform/admin';
import { parseHydraDocumentation } from '@api-platform/api-doc-parser';
import AppBar from './AppBar';
import { LoginForm } from './LoginForm';
import DocContext from './DocContext';
import authProvider from 'utils/authProvider';
import { ENTRYPOINT } from 'config/entrypoint';

// todo Waiting for https://github.com/api-platform/admin/issues/372
const getHeaders = () =>
  localStorage.getItem('token')
    ? {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      }
    : {};
const fetchHydra = (url, options = {}) =>
  baseFetchHydra(url, {
    ...options,
    headers: getHeaders,
  });
const RedirectToLogin = () => {
  const introspect = useIntrospection();

  if (localStorage.getItem('token')) {
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
    localStorage.removeItem('token');

    setRedirectToLogin(true);

    return {
      api,
      response,
      status,
    };
  }
};
const dataProvider = (setRedirectToLogin) =>
  baseHydraDataProvider({
    useEmbedded: false,
    entrypoint: ENTRYPOINT,
    httpClient: fetchHydra,
    apiDocumentationParser: apiDocumentationParser(setRedirectToLogin),
  });

const messages = {
  fr: frenchMessages,
  en: englishMessages,
};
const i18nProvider = polyglotI18nProvider(
  (locale) => (messages[locale] ? messages[locale] : messages.en),
  resolveBrowserLocale(),
);

const LoginPage = () => (
  <Login
    sx={{
      backgroundImage:
        'radial-gradient(circle at 50% 14em, #90dfe7 0%, #288690 60%, #288690 100%)',
      [`& .${LoginClasses.icon}`]: {
        backgroundColor: 'secondary.main',
      },
    }}>
    <LoginForm />
  </Login>
);

const MyLayout = (props) => <Layout {...props} appBar={AppBar} />;

const AdminUI = () => {
  const { docType, openApiEntrypoint, openApiDocEntrypoint } =
    useContext(DocContext);
  const [redirectToLogin, setRedirectToLogin] = useState(false);

  return docType === 'hydra' ? (
    <HydraAdmin
      dataProvider={dataProvider(setRedirectToLogin)}
      authProvider={authProvider}
      entrypoint={window.origin}
      i18nProvider={i18nProvider}
      layout={MyLayout}
      loginPage={LoginPage}>
      <CustomRoutes>
        {redirectToLogin ? (
          <Route path="/" element={<RedirectToLogin />} />
        ) : null}
      </CustomRoutes>
    </HydraAdmin>
  ) : (
    <OpenApiAdmin
      authProvider={authProvider}
      entrypoint={openApiEntrypoint}
      docEntrypoint={openApiDocEntrypoint}
      i18nProvider={i18nProvider}
      layout={MyLayout}
      loginPage={LoginPage}
    />
  );
};

const store = localStorageStore();

const AdminWithContext = () => {
  const [docType, setDocType] = useState(store.getItem<string>('docType', 'hydra'));
  const [openApiEntrypoint, setOpenApiEntrypoint] = useState(window.origin);
  const [openApiDocEntrypoint, setOpenApiDocEntrypoint] = useState(
    `${window.origin}/docs.json`,
  );

  return (
    <DocContext.Provider
      value={{
        docType,
        openApiEntrypoint,
        openApiDocEntrypoint,
        setDocType,
        setOpenApiEntrypoint,
        setOpenApiDocEntrypoint,
      }}>
      <AdminUI />
    </DocContext.Provider>
  );
};

const Admin = () => (
  <>
    <Head>
      <title>API Platform Admin</title>
    </Head>

    <AdminWithContext />
  </>
);

export default Admin;
