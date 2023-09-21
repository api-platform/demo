import { SelectInput, SelectInputProps } from "react-admin";

export const ConditionInput = (props: SelectInputProps) => (
  <SelectInput {...props} choices={[
    { id: "https://schema.org/NewCondition", name: "New" },
    { id: "https://schema.org/RefurbishedCondition", name: "Refurbished" },
    { id: "https://schema.org/DamagedCondition", name: "Damaged" },
    { id: "https://schema.org/UsedCondition", name: "Used" },
  ]}/>
);
