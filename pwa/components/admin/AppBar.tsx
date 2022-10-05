import { useContext, useState } from 'react';
import {
  AppBar,
  AppBarClasses,
  LocalesMenuButton,
  ToggleThemeButton,
  useAuthProvider,
  useStore,
} from 'react-admin';
import type { AppBarProps } from 'react-admin';
import { Box, Button, Menu, MenuItem, Typography } from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';

import DocContext from './DocContext';
import HydraLogo from './HydraLogo';
import OpenApiLogo from './OpenApiLogo';
import Logo from './Logo';
import { darkTheme, lightTheme } from './themes';

const DocTypeMenuButton = () => {
  const [anchorEl, setAnchorEl] = useState(null);
  const [, setStoreDocType] = useStore('docType', 'hydra');
  const { docType, setDocType } = useContext(DocContext);

  const open = Boolean(anchorEl);
  // @ts-ignore
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
