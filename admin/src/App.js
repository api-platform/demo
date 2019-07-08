import React from 'react';
import { createMuiTheme } from '@material-ui/core/styles';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';
import {
  HydraAdmin,
  hydraClient,
  fetchHydra as baseFetchHydra,
} from '@api-platform/admin';
import { Route, Redirect } from 'react-router-dom';

import authProvider from './authProvider';
import { Layout } from './layout';

const theme = createMuiTheme({
  palette: {
    primary: {
      main: '#38a9b4',
      contrastText: '#fff',
    },
    secondary: {
      main: '#288690',
    },
  },
});

const fetchHeaders = {
  Authorization: `Bearer ${localStorage.getItem('token')}`,
};
const fetchHydra = (url, options = {}) =>
  baseFetchHydra(url, {
    ...options,
    headers: new Headers(fetchHeaders),
  });
const dataProvider = api => hydraClient(api, fetchHydra);
const apiDocumentationParser = entrypoint =>
  parseHydraDocumentation(entrypoint, {
    headers: new Headers(fetchHeaders),
  })
    .then(
      ({ api }) => ({ api }),
      result => {
        const { api, status } = result;

        if (status === 401 || status === 403) {
          return Promise.resolve({
            api,
            status,
            customRoutes: [
              <Route path="/" render={() => <Redirect to="/login" />} />,
            ],
          });
        }

        return Promise.reject(result);
      }
    );

export default () => (
  <HydraAdmin
    appLayout={Layout}
    theme={theme}
    entrypoint={process.env.REACT_APP_API_ENTRYPOINT}
    authProvider={authProvider}
    apiDocumentationParser={apiDocumentationParser}
    dataProvider={dataProvider}
  />
);
