import { SelectInput, SelectInputProps } from "react-admin";

export const ConditionInput = (props: SelectInputProps) => (
  <SelectInput {...props} choices={[
    /*todo translate condition*/
    { id: "https://schema.org/NewCondition", name: "NewCondition" },
    { id: "https://schema.org/RefurbishedCondition", name: "RefurbishedCondition" },
    { id: "https://schema.org/DamagedCondition", name: "DamagedCondition" },
    { id: "https://schema.org/UsedCondition", name: "UsedCondition" },
  ]}/>
);
