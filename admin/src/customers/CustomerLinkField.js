import React from "react";
import { Link } from "react-admin";

import FullNameField from "./FullNameField";

const CustomerLinkField = props =>
  props.record ? (
    <Link to={`/customers/${props.record.id}`}>
      <FullNameField {...props} />
    </Link>
  ) : null;

CustomerLinkField.defaultProps = {
  source: "customer_id",
  addLabel: true
};

export default CustomerLinkField;
