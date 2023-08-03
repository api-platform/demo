import { FieldGuesser, ListGuesser, type ListGuesserProps } from "@api-platform/admin";
import { TextField } from "react-admin";

import { BookField } from "@/components/admin/review/BookField";

export const List = (props: ListGuesserProps) => (
  <ListGuesser {...props} title="Reviews" exporter={false} hasCreate={false}>
    <TextField source="user.name" label="Author"/>
    <BookField source="book"/>
    <FieldGuesser source="publishedAt"/>
  </ListGuesser>
);
