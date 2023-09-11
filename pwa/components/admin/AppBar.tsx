import { useContext, useState } from "react";
import { AppBar, AppBarClasses, UserMenu, Logout, useStore } from "react-admin";
import { type AppBarProps } from "react-admin";
import { Button, Menu, MenuItem, Typography } from "@mui/material";

import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import DocContext from "@/components/admin/DocContext";
import HydraLogo from "@/components/admin/HydraLogo";
import OpenApiLogo from "@/components/admin/OpenApiLogo";
import Logo from "@/components/admin/Logo";

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

const CustomAppBar = ({ ...props }: AppBarProps) => {
  return (
    <AppBar userMenu={
      <UserMenu>
        <Logout redirectTo={`${window.location.origin}/books`}/>
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
