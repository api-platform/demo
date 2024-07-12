import { ForwardedRef, forwardRef } from "react";
import { LogoutClasses, useTranslate } from "react-admin";

import { ListItemIcon, ListItemText, MenuItem } from "@mui/material";
import ExitIcon from "@mui/icons-material/PowerSettingsNew";
import { signOut, useSession } from "next-auth/react";

import { NEXT_PUBLIC_OIDC_SERVER_URL } from "../../../config/keycloak";

const Logout = forwardRef((props, ref: ForwardedRef<any>) => {
  const { data: session } = useSession();
  const translate = useTranslate();

  if (!session) {
    return;
  }

  const handleClick = () =>
    signOut({
      // @ts-ignore
      callbackUrl: `${NEXT_PUBLIC_OIDC_SERVER_URL}/protocol/openid-connect/logout?id_token_hint=${session.idToken}&post_logout_redirect_uri=${window.location.origin}`,
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
        {translate("ra.auth.logout", { _: "Logout" })}
      </ListItemText>
    </MenuItem>
  );
});
Logout.displayName = "Logout";

export default Logout;
