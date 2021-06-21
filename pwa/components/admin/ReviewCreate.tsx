import {CreateGuesser, InputGuesser} from "@api-platform/admin";
import {AutocompleteInput, ReferenceInput} from "react-admin";

const ReviewCreate = (props) => (
  <CreateGuesser {...props}>
    <InputGuesser source="author" />
    {/* Use react-admin components directly when you want complex inputs. */}
    <ReferenceInput
      source="book"
      reference="books"
      label="Books"
      filterToQuery={(searchText) => ({ title: searchText })}
    >
      <AutocompleteInput optionText="title" />
    </ReferenceInput>

    <InputGuesser source="rating" />

    {/* While deprecated fields are hidden by default, using an explicit InputGuesser component allows to add them back. */}
    <InputGuesser source="letter" />

    <InputGuesser source="body" />
    <InputGuesser source="publicationDate" />
  </CreateGuesser>
);

export default ReviewCreate;
