import React, { createElement, forwardRef } from "react";
import { useAuthProvider, Logout, MenuItemLink, UserMenu } from "react-admin";
import AccountCircle from "@material-ui/icons/AccountCircle";
import Check from "@material-ui/icons/Check";
import LockIcon from "@material-ui/icons/LockOutlined";
import AppBar from "@api-platform/admin/lib/layout/AppBar";
import Layout from "@api-platform/admin/lib/layout/Layout";

const handleLogin = (onClick) => () => {
  localStorage.setItem("authentication", true);
  onClick();
};

const LoginButton = forwardRef(({ onClick }, ref) => {
  const logged = useAuthProvider().checkLogged();

  return (
    !logged && (
      <MenuItemLink
        ref={ref}
        to="/login"
        primaryText="Login"
        leftIcon={<LockIcon />}
        onClick={handleLogin(onClick)}
      />
    )
  );
});

const LogoutButton = () => {
  const logged = useAuthProvider().checkLogged();

  return logged && <Logout />;
};

const AccountIcon = () => {
  const logged = useAuthProvider().checkLogged();

  return logged ? (
    <>
      <AccountCircle />
      <Check />
    </>
  ) : (
    <AccountCircle />
  );
};

const CustomUserMenu = (
  <UserMenu icon={createElement(AccountIcon)}>
    <LoginButton />
  </UserMenu>
);
const CustomAppBar = ({ logout, ...props }) => (
  <AppBar
    userMenu={CustomUserMenu}
    logout={createElement(LogoutButton)}
    {...props}
  />
);
const CustomLayout = (props) => <Layout appBar={CustomAppBar} {...props} />;

export default CustomLayout;
