import { FieldGuesser, ShowGuesser, type ShowGuesserProps } from "@api-platform/admin";
import { TextField } from "react-admin";

import { RatingField } from "@/components/admin/review/RatingField";
import { BookField } from "@/components/admin/review/BookField";

export const Show = (props: ShowGuesserProps) => (
  <ShowGuesser {...props} title="Show review">
    <TextField source="user.name" label="Author"/>
    <BookField source="book"/>
    <FieldGuesser source="publishedAt"/>
    <RatingField source="rating"/>
    <FieldGuesser source="body"/>
  </ShowGuesser>
);
