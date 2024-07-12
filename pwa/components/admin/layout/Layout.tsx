import { Layout, type LayoutProps } from "react-admin";
import AppBar from "./AppBar";
import Menu from "./Menu";

const MyLayout = (props: React.JSX.IntrinsicAttributes & LayoutProps) => (
  <Layout {...props} appBar={AppBar} menu={Menu} />
);

export default MyLayout;
