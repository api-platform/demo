import React from "react";
import Avatar from "@material-ui/core/Avatar";

const AvatarField = ({ record, size = "25", className }) =>
  record ? (
    <Avatar
      src={`${record.avatar}?size=${size}x${size}`}
      style={{ width: parseInt(size, 10), height: parseInt(size, 10) }}
      className={className}
    />
  ) : null;

export default AvatarField;
