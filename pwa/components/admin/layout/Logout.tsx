import {ForwardedRef, forwardRef} from "react";
import {LogoutClasses, useTranslate} from "react-admin";

import {ListItemIcon, ListItemText, MenuItem} from "@mui/material";
import ExitIcon from "@mui/icons-material/PowerSettingsNew";

import {useSession, signOutWithKeycloak} from "../../../hooks/useAuth";

const Logout = forwardRef((props, ref: ForwardedRef<any>) => {
  const { data: session } = useSession();
  const translate = useTranslate();

  if (!session) {
    return;
  }

  const handleClick = () =>
    signOutWithKeycloak(window.location.origin);

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
