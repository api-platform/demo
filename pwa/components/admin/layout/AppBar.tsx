import { AppBar, UserMenu, TitlePortal } from "react-admin";

import Logo from "../Logo";
import Logout from "./Logout";
import DocTypeMenuButton from "./DocTypeMenuButton";

const CustomAppBar = () => (
  <AppBar
    userMenu={
      <UserMenu>
        <Logout />
      </UserMenu>
    }
  >
    <TitlePortal />
    <div className="flex-1">
      <Logo />
    </div>
    <DocTypeMenuButton />
  </AppBar>
);

export default CustomAppBar;
