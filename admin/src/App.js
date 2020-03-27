import React from "react";
import {
  HydraAdmin,
  ResourceGuesser,
} from "@api-platform/admin";

import customers from "./customers";
import commands from "./commands";
import products from "./products";

export default () => (
  <HydraAdmin entrypoint={process.env.REACT_APP_API_ENTRYPOINT}>
    <ResourceGuesser name="customers" {...customers} />
    <ResourceGuesser name="commands" {...commands} />
    <ResourceGuesser name="products" {...products} />
  </HydraAdmin>
);
