import { useContext, useState } from "react";
import { useStore } from "react-admin";
import { Button, Menu, MenuItem } from "@mui/material";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";

import DocContext from "../DocContext";
import HydraLogo from "./HydraLogo";
import OpenApiLogo from "./OpenApiLogo";

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
  const changeDocType = (docType: string) => {
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
        onClick={handleClick}
        startIcon={docType === "hydra" ? <HydraLogo /> : <OpenApiLogo />}
      >
        {docType === "hydra" ? "Hydra" : "OpenAPI"}
        <ExpandMoreIcon fontSize="small" />
      </Button>
      <Menu
        id="doc-type-menu"
        anchorEl={anchorEl}
        open={open}
        onClose={handleClose}
        MenuListProps={{
          "aria-labelledby": "basic-button",
        }}
      >
        <MenuItem onClick={() => changeDocType("hydra")}>Hydra</MenuItem>
        <MenuItem onClick={() => changeDocType("openapi")}>OpenAPI</MenuItem>
      </Menu>
    </>
  );
};

export default DocTypeMenuButton;
