import { FieldGuesser, ShowGuesser } from "@api-platform/admin";
import { TextField, Labeled } from "react-admin";

import { RatingField } from "./RatingField";
import { BookField } from "./BookField";

export const ReviewsShow = () => (
  <ShowGuesser title="Show review">
    <TextField source="user.name" label="Author" />
    <BookField source="book" />
    <FieldGuesser source="publishedAt" />
    <Labeled label="Rating">
      <RatingField />
    </Labeled>
    <FieldGuesser source="body" />
  </ShowGuesser>
);
