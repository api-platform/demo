import { Menu as ReactAdminMenu } from "react-admin";
import MenuBookIcon from "@mui/icons-material/MenuBook";
import CommentIcon from "@mui/icons-material/Comment";

const Menu = () => (
  <ReactAdminMenu>
    {/*@ts-ignore*/}
    <ReactAdminMenu.Item to="/admin/books" primaryText="Books" leftIcon={<MenuBookIcon/>}/>
    {/*@ts-ignore*/}
    <ReactAdminMenu.Item to="/admin/reviews" primaryText="Reviews" leftIcon={<CommentIcon/>}/>
  </ReactAdminMenu>
);
export default Menu;
