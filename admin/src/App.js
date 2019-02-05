import React from 'react';
import { HydraAdmin } from '@api-platform/admin';
import { createMuiTheme } from '@material-ui/core/styles';

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

export default () => <HydraAdmin appLayout={Layout} theme={theme} entrypoint={process.env.REACT_APP_API_ENTRYPOINT}/>;
