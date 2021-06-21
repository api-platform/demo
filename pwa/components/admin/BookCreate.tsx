import {CreateGuesser, InputGuesser} from "@api-platform/admin";
import {FileField, FileInput} from "react-admin";

const BookCreate = (props) => (
  <CreateGuesser {...props}>
    <InputGuesser source="isbn" />
    <InputGuesser source="title" />
    <InputGuesser source="description" />
    <InputGuesser source="author" />
    <InputGuesser source="publicationDate" />
    <InputGuesser source="reviews" />
    <FileInput source="frontCoverFile">
      <FileField source="src" title="title" />
    </FileInput>
  </CreateGuesser>
);

export default BookCreate;
