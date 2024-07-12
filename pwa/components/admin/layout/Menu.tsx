import { Menu } from "react-admin";
import MenuBookIcon from "@mui/icons-material/MenuBook";
import CommentIcon from "@mui/icons-material/Comment";

const CustomMenu = () => (
  <Menu>
    <Menu.Item
      to="/admin/books"
      primaryText="Books"
      leftIcon={<MenuBookIcon />}
    />
    <Menu.Item
      to="/admin/reviews"
      primaryText="Reviews"
      leftIcon={<CommentIcon />}
    />
  </Menu>
);
export default CustomMenu;
