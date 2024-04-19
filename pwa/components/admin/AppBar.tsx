import {ForwardedRef, forwardRef, useContext, useState} from "react";
import { AppBar, AppBarClasses, LogoutClasses, UserMenu, useTranslate, useStore } from "react-admin";
import { type AppBarProps } from "react-admin";
import {Button, ListItemIcon, ListItemText, Menu, MenuItem, Typography} from "@mui/material";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import ExitIcon from "@mui/icons-material/PowerSettingsNew";
import { signOut, useSession } from "next-auth/react";

import DocContext from "../../components/admin/DocContext";
import HydraLogo from "../../components/admin/HydraLogo";
import OpenApiLogo from "../../components/admin/OpenApiLogo";
import Logo from "../../components/admin/Logo";
import {OIDC_SERVER_URL} from "../../config/keycloak";

const DocTypeMenuButton = () => {
  const [anchorEl, setAnchorEl] = useState(null);
  const [, setStoreDocType] = useStore("docType", "hydra");
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
    <>
      <Button
        color="inherit"
        aria-controls={open ? "doc-type-menu" : undefined}
        aria-haspopup="true"
        aria-expanded={open ? "true" : undefined}
        onClick={handleClick}>
        {docType === "hydra" ? (
          <>
            <HydraLogo/> Hydra
          </>
        ) : (
          <>
            <OpenApiLogo/> OpenAPI
          </>
        )}
        <ExpandMoreIcon fontSize="small"/>
      </Button>
      <Menu
        id="doc-type-menu"
        anchorEl={anchorEl}
        open={open}
        onClose={handleClose}
        MenuListProps={{
          "aria-labelledby": "basic-button",
        }}>
        <MenuItem onClick={changeDocType("hydra")}>Hydra</MenuItem>
        <MenuItem onClick={changeDocType("openapi")}>OpenAPI</MenuItem>
      </Menu>
    </>
  );
};

const Logout = forwardRef((props, ref: ForwardedRef<any>) => {
  const { data: session } = useSession();
  const translate = useTranslate();

  if (!session) {
    return;
  }

  const handleClick = () => signOut({
    // @ts-ignore
    callbackUrl: `${OIDC_SERVER_URL}/protocol/openid-connect/logout?id_token_hint=${session.idToken}&post_logout_redirect_uri=${window.location.origin}`,
  });

  return (
    <MenuItem
      className="logout"
      onClick={handleClick}
      ref={ref}
      component="li"
      {...props}
    >
      <ListItemIcon className={LogoutClasses.icon}>
        <ExitIcon fontSize="small" />
      </ListItemIcon>
      <ListItemText>
        {translate('ra.auth.logout', { _: 'Logout' })}
      </ListItemText>
    </MenuItem>
  );
});
Logout.displayName = "Logout";

const CustomAppBar = ({ ...props }: AppBarProps) => {
  return (
    <AppBar userMenu={
      <UserMenu>
        <Logout/>
      </UserMenu>
    } {...props}>
      <Typography
        variant="h6"
        color="inherit"
        className={`${AppBarClasses.title} w-[200px]`}
        id="react-admin-title"
      />
      <div className="flex-1">
        <Logo/>
      </div>
      <DocTypeMenuButton/>
    </AppBar>
  );
};

export default CustomAppBar;
