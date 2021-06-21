import {FieldGuesser, ListGuesser} from "@api-platform/admin";
import {ReferenceField, TextField} from "react-admin";

const ReviewList = (props) => (
  <ListGuesser {...props}>
    <FieldGuesser source="author" />
    <FieldGuesser source="book" />
    {/* Use react-admin components directly when you want complex fields. */}
    <ReferenceField label="Book's title" source="book" reference="books">
      <TextField source="title" />
    </ReferenceField>

    {/* While deprecated fields are hidden by default, using an explicit FieldGuesser component allows to add them back. */}
    <FieldGuesser source="letter" />
  </ListGuesser>
);

export default ReviewList;
