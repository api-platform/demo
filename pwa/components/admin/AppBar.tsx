import { useContext, useState } from 'react';
import {
  AppBar,
  AppBarClasses,
  Form,
  LocalesMenuButton,
  TextInput,
  ToggleThemeButton,
  required,
  useAuthProvider,
  useStore,
} from 'react-admin';
import type { AppBarProps } from 'react-admin';
import { useIntrospection } from '@api-platform/admin';
import { useNavigate } from 'react-router';
import {
  Box,
  Button,
  CardContent,
  Dialog,
  Menu,
  MenuItem,
  Typography,
} from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';

import DocContext from './DocContext';
import HydraLogo from './HydraLogo';
import OpenApiLogo from './OpenApiLogo';
import Logo from './Logo';
import { darkTheme, lightTheme } from './themes';

const OpenApiDocEntrypointDialog = ({ onClose, open }) => {
  const {
    openApiEntrypoint,
    openApiDocEntrypoint,
    setOpenApiEntrypoint,
    setOpenApiDocEntrypoint,
  } = useContext(DocContext);
  const navigate = useNavigate();
  const introspect = useIntrospection();

  const handleClose = () => {
    onClose();
  };

  const submit = (values) => {
    const { entrypoint, docEntrypoint } = values;
    setOpenApiEntrypoint(entrypoint);
    setOpenApiDocEntrypoint(docEntrypoint);
    onClose();
    navigate('/');
    introspect();
  };

  return (
    <Dialog onClose={handleClose} open={open}>
      <CardContent>
        <Form
          noValidate
          mode="onChange"
          onSubmit={submit}
          defaultValues={{
            entrypoint: openApiEntrypoint,
            docEntrypoint: openApiDocEntrypoint,
          }}>
          <TextInput
            autoFocus
            source="entrypoint"
            label="API URL"
            validate={required()}
            fullWidth
          />
          <TextInput
            autoFocus
            source="docEntrypoint"
            label="Documentation URL"
            validate={required()}
            fullWidth
          />
          <Button variant="contained" type="submit" color="primary" fullWidth>
            OK
          </Button>
        </Form>
      </CardContent>
    </Dialog>
  );
};

const OpenApiDocEntrypointButton = () => {
  const [open, setOpen] = useState(false);

  const handleClickOpen = () => {
    setOpen(true);
  };

  const handleClose = () => {
    setOpen(false);
  };

  return (
    <div>
      <Button color="inherit" onClick={handleClickOpen}>
        URL
      </Button>
      <OpenApiDocEntrypointDialog open={open} onClose={handleClose} />
    </div>
  );
};

const DocTypeMenuButton = () => {
  const [anchorEl, setAnchorEl] = useState(null);
  const [, setStoreDocType] = useStore('docType', 'hydra');
  const { docType, setDocType } = useContext(DocContext);

  const open = Boolean(anchorEl);
  const handleClick = (event) => {
    setAnchorEl(event.currentTarget);
  };
  const handleClose = () => {
    setAnchorEl(null);
  };
  const changeDocType = (docType: string) => () => {
    setStoreDocType(docType);
    setDocType(docType);
    handleClose();
  };

  return (
    <div>
      <Button
        color="inherit"
        aria-controls={open ? 'doc-type-menu' : undefined}
        aria-haspopup="true"
        aria-expanded={open ? 'true' : undefined}
        onClick={handleClick}>
        {docType === 'hydra' ? (
          <>
            <HydraLogo /> Hydra
          </>
        ) : (
          <>
            <OpenApiLogo /> OpenAPI
          </>
        )}
        <ExpandMoreIcon fontSize="small" />
      </Button>
      <Menu
        id="doc-type-menu"
        anchorEl={anchorEl}
        open={open}
        onClose={handleClose}
        MenuListProps={{
          'aria-labelledby': 'basic-button',
        }}>
        <MenuItem onClick={changeDocType('hydra')}>Hydra</MenuItem>
        <MenuItem onClick={changeDocType('openapi')}>OpenAPI</MenuItem>
      </Menu>
    </div>
  );
};

const CustomAppBar = ({ classes, userMenu, ...props }: AppBarProps) => {
  const authProvider = useAuthProvider();
  const { docType } = useContext(DocContext);

  return (
    <AppBar userMenu={userMenu ?? !!authProvider} {...props}>
      <Typography
        variant="h6"
        color="inherit"
        className={AppBarClasses.title}
        id="react-admin-title"
      />
      <Logo />
      <Box component="span" sx={{ flex: 0 }} />
      <DocTypeMenuButton />
      {docType === 'openapi' && <OpenApiDocEntrypointButton />}
      <Box component="span" sx={{ flex: 0.5 }} />
      <LocalesMenuButton
        languages={[
          { locale: 'en', name: 'English' },
          { locale: 'fr', name: 'Français' },
        ]}
      />
      <ToggleThemeButton lightTheme={lightTheme} darkTheme={darkTheme} />
    </AppBar>
  );
};

export default CustomAppBar;
